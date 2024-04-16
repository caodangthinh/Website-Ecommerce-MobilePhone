import React, { useState } from 'react';
import Layout from '../../components/Layout/layout';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';


const Register = () => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [phone, setPhone] = useState('');
    const [address, setAddress] = useState('');
    const [answer, setAnswer] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await axios.post(`${process.env.REACT_APP_API}/api/v1/auth/register`, { name, email, password, address, phone, answer });
            if (res && res.data.success) {
                toast.success(res.data && res.data.message);
                navigate('/login');
            } else {
                toast.error(res.data.message);
            }

        } catch (error) {
            console.log(error);
            toast.error('Error')
        }

    };

    return (
        <Layout title={'Register - PhoneShop'}>
            <div className="wrapper" id="container">
                <div className="form-container login-container">
                    <div className="form-login-sign">
                        <form onSubmit={handleSubmit}>
                            <div className="title-form">
                                <h1>Register</h1>
                            </div>
                            <div className="field">
                                <input type="text" onChange={(e) => setName(e.target.value)} value={name} placeholder="Enter your Name" className="password" id="name" name="name" required />
                            </div>
                            <div className="field">
                                <input type="email" onChange={(e) => setEmail(e.target.value)} value={email} placeholder="Enter your Email" className="password" id="email" name="email" required />
                            </div>
                            <div className="field">
                                <input type="password" onChange={(e) => setPassword(e.target.value)} value={password} placeholder="Enter your Password" className="password" id="password" name="password" required />
                            </div>
                            <div className="field">
                                <input type="text" onChange={(e) => setPhone(e.target.value)} value={phone} placeholder="Enter your Phone" className="password" id="phone" name="phone" required />
                            </div>
                            <div className="field">
                                <input type="text" onChange={(e) => setAddress(e.target.value)} value={address} placeholder="Enter your Address" className="password" id="address" name="address" required />
                            </div>
                            <div className="field">
                                <input onChange={(e) => setAnswer(e.target.value)} value={answer} placeholder="Enter your Answer" className="password" id="answer" name="answer" required />
                            </div>
                            <button type="submit" className="btn-form">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </Layout>
    );
};

export default Register;